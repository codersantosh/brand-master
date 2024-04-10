/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';

import { cloneDeep } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcNestedObjUpdateByKey1,
	AtrcControlText,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { login = {} } = dbSettings;
	const { title = '' } = login;

	return (
		<AtrcContent>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlText
					label={__('Login page title', 'brand-master')}
					value={title}
					onChange={(newVal) => {
						const updatedSettings = AtrcNestedObjUpdateByKey1({
							settings: login,
							key1: 'title',
							val1: newVal,
						});
						dbUpdateSetting('login', updatedSettings);
					}}
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
						localStorageClone.bmLtDocs1 = !localStorageClone.bmLtDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<AtrcPanelBody
					className={classNames(AtrcPrefix('m-0'))}
					title={__(
						'How can I change the page title of my WordPress login page?',
						'brand-master'
					)}
					initialOpen={true}>
					<AtrcText
						tag='p'
						className={classNames(AtrcPrefix('m-0'), 'at-m')}>
						{__(
							'Title settings empower you to customize the page title displayed on your WordPress login page, allowing you to tailor it according to your preferences or branding requirements.',
							'brand-master'
						)}
					</AtrcText>
				</AtrcPanelBody>
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

	const { bmLtDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('WordPress login page settings', 'brand-master')}
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
					renderSidebar={!bmLtDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmLtDocs1 ? 'at-col-12' : 'at-col-7',
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
