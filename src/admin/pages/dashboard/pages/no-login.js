/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';
import { cloneDeep } from 'lodash';

/*Atrc*/
import AtrcText from 'atrc/build/atoms/text';
import AtrcControlSelectPost from 'atrc/build/controls/control-select-post';
import AtrcPanelBody from 'atrc/build/molecules/panel-body';
import AtrcPanelRow from 'atrc/build/molecules/panel-row';
import { AtrcNestedObjUpdateByKey1 } from 'atrc/build/molecules/repeater/utils';
import AtrcContent from 'atrc/build/organisms/content';
import AtrcPrefix from 'atrc/build/prefix-vars';
import AtrcTitleTemplate1 from 'atrc/build/templates/components/title/template-1';
import AtrcWireFrameContentSidebar from 'atrc/build/wireframe/content-sidebar';
import AtrcWireFrameHeaderContentFooter from 'atrc/build/wireframe/header-content-footer';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { dashboard = {} } = dbSettings;

	const { noLoginContent = 0 } = dashboard;

	return (
		<AtrcContent className={classNames(AtrcPrefix('cont-dashboard'))}>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlSelectPost
					label={__('Select page for non-logged-in visitor', 'brand-master')}
					wrapProps={{
						className: 'at-flx-grw-1',
					}}
					value={noLoginContent}
					onChange={(newVal) => {
						const updatedSettings = AtrcNestedObjUpdateByKey1({
							settings: dashboard,
							key1: 'noLoginContent',
							val1: newVal,
						});
						dbUpdateSetting('dashboard', updatedSettings);
					}}
					postType='page'
				/>
			</AtrcPanelRow>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmdSdDocs1 = !localStorageClone.bmdSdDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__(
							'When does the content of the "Select Page For Non-Logged-in Visitor" appear?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'If a user navigates directly to the frontend dashboard without logging in, the content of the chosen page on the "Select Page For Non-Logged-In Visitor" section will be displayed for them.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmdSdDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('Frontend Dashboard settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
						className: 'at-p',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmdSdDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmdSdDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
